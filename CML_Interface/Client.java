package CML_Interface;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.net.Socket;
import java.util.concurrent.*;

public class Client {
    private static BufferedReader reader;
    private static PrintWriter writer;
    private static BufferedReader userReader = new BufferedReader(new InputStreamReader(System.in));
    private static ExecutorService executorService = Executors.newSingleThreadExecutor();
    private static boolean awaitingAnswer = false;

    public static void main(String[] args) {
        try (Socket socket = new Socket("localhost", 8888)) {
            // Initialize reader and writer
            reader = new BufferedReader(new InputStreamReader(socket.getInputStream()));
            writer = new PrintWriter(socket.getOutputStream(), true);

            // Read server welcome message and print it
            System.out.println(readServerResponse());

            // Display available commands to the user
            printCommandsHelp();

            // Client command processing loop
            while (true) {
                String userInput = readUserInput("Enter command: ");
                if (userInput.equalsIgnoreCase("exit")) {
                    System.out.println("Exiting...");
                    break;
                }
                sendCommandToServer(userInput);
                processServerResponses();
            }
        } catch (IOException e) {
            e.printStackTrace();
        } finally {
            executorService.shutdown();
        }
    }

    private static void processServerResponses() throws IOException {
        String response;
        while ((response = readServerResponse()) != null) {
            if (response.startsWith("Challenge")) {
                handleChallenge(response);
            } else if (response.startsWith("availablechallenges ")) {
                handleAvailableChallenges(response.substring("availablechallenges ".length()));
            } else if (response.startsWith("availableapplicants")) {

                handleAvailableApplicants(response.substring("availableapplicants ".length()));
            }

            else {
                System.out.println(response);
                break;
            }
        }
    }

    private static void handleChallenge(String response) throws IOException {
        System.out.println(response); // Display challenge details
        long startTime = System.currentTimeMillis();
        long challengeDuration = 10 * 60 * 1000; // Example: 10 minutes in milliseconds

        executorService.submit(() -> {
            try {
                while (System.currentTimeMillis() - startTime < challengeDuration) {
                    if (awaitingAnswer) {
                        String answer = readUserInput("Answer: ");
                        sendCommandToServer(answer);
                        awaitingAnswer = false;
                    }

                    String challengeResponse = readServerResponse();
                    if (challengeResponse == null)
                        break;

                    if (challengeResponse.startsWith("Question: ")) {
                        System.out.println(challengeResponse); // Display the question
                        awaitingAnswer = true;
                    } else if (challengeResponse.startsWith("Challenge submitted successfully.")
                            || challengeResponse.startsWith("Your score: ")) {
                        System.out.println(challengeResponse);
                        break;
                    } else if (!challengeResponse.startsWith("Remaining time:")) {
                        System.out.println(challengeResponse);
                    }

                    long elapsedTime = System.currentTimeMillis() - startTime;
                    long remainingTime = challengeDuration - elapsedTime;
                    System.out.printf("Remaining time: %d minutes %d seconds%n",
                            remainingTime / 60000, (remainingTime / 1000) % 60);

                    // Add a short sleep to prevent busy waiting
                    Thread.sleep(1000);
                }
            } catch (IOException | InterruptedException e) {
                e.printStackTrace();
            }
        });
    }

    private static String readServerResponse() throws IOException {
        return reader.readLine();
    }

    private static String readUserInput(String prompt) {
        System.out.print(prompt);
        try {
            return userReader.readLine();
        } catch (IOException e) {
            e.printStackTrace();
            return null;
        }
    }

    private static void sendCommandToServer(String command) {
        writer.println(command);
    }

    private static void handleAvailableChallenges(String statementData) {
        String[] rows = statementData.split("\t");

        // Print table headers
        System.out.println("                           Available Challenges \n");
        System.out.println(
                "Challenge_ID   Title           Description                Status    Number_Of_Questions     start_Date                   End_Date                     Duration  ");

        for (String row : rows) {
            // Split each row into individual columns
            String[] columns = row.split(" ");
            // Print each column separated by spaces
            for (String column : columns) {
                System.out.print(column + "    ");
            }
            System.out.println(); // Move to the next row
        }
        String command = readUserInput("Enter command: ");
        sendCommandToServer(command);
    }

    private static void handleAvailableApplicants(String statementData) {
        String[] rows = statementData.split("\t");

        // Print table headers
        System.out.println("                           Available Applicants \n");
        System.out.println(
                "Username     Firstname      LastName      School Registration Number  ");

        for (String row : rows) {
            // Split each row into individual columns
            String[] columns = row.split(" ");
            // Print each column separated by spaces
            for (String column : columns) {
                System.out.print(column + "    ");
            }
            System.out.println(); // Move to the next row
        }
        String command = readUserInput("Enter command: ");
        sendCommandToServer(command);
    }

    private static void printCommandsHelp() {
        String[] commands = {
                "login <username> <password> - Log in to the system usage e.g login lee lee123.com  ",
                "register <username> <firstname> <lastname> <email> <dob> <school_reg_no> <image_path> - Register a new user",
                "viewChallenges - View all available challenges  e.g  viewchallenges  ",
                "attemptChallenge <challenge_ID> - Attempt a specified challenge e.g attemptchallenge  5 ",
                "viewApplicants - View all applicants pending confirmation",
                "confirm <yes/no> <username> - Confirm or reject an applicant",
                "viewReports - View analytics and reports",
                "exit - Exit the client"
        };

        // Find the maximum width of the command strings
        int maxWidth = 0;
        for (String command : commands) {
            if (command.length() > maxWidth) {
                maxWidth = command.length();
            }
        }
        maxWidth += 4; // Adding padding for aesthetics

        int totalCommands = commands.length;

        // Print the rhombus
        for (int i = 0; i < totalCommands; i++) {
            int paddingFactor = Math.min(i, totalCommands - i - 1);
            printRhombusLine(commands[i], maxWidth, paddingFactor, totalCommands);
        }
    }

    private static void printRhombusLine(String command, int maxWidth, int paddingFactor, int totalCommands) {
        int outerPadding = paddingFactor;
        int commandPadding = (maxWidth - command.length() - 2);

        // Print the leading spaces for the rhombus shape
        for (int j = 0; j < outerPadding; j++) {
            System.out.print(" ");
        }

        // Print the command line with surrounding asterisks
        System.out.print("* ");
        System.out.print(command);
        for (int j = 0; j < commandPadding; j++) {
            System.out.print(" ");
        }
        System.out.println(" *");

        // Print the line of asterisks separating commands, except after the last command
        if (paddingFactor < totalCommands / 2) {
            for (int j = 0; j <= outerPadding; j++) {
                System.out.print(" ");
            }
            for (int j = 0; j < maxWidth - 2 * outerPadding; j++) {
                System.out.print("*");
            }
            System.out.println();
        }
    }
}
